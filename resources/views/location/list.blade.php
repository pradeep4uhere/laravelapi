<!DOCTYPE html>
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
</head>
<body>

<div class="container">
  <div class="row">
    <div class="col-md-9">
  <h2>Location List</h2>
  <p><i class="fa fa-plus"></i><a href="addnewlocation">Add Location</a></p>
  <div class="panel-group" id="accordion">
    <?php $count = 1; foreach($location as $item){ ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h4 class="panel-title">
          <a data-toggle="collapse" data-parent="#accordion" href="#collapse{{$count}}">{{$item['location_name']}}</a>
        </h4>
      </div>
      <div id="collapse{{$count}}" class="panel-collapse collapse <?php if($count==1){ echo "isn";}?>">
        <div class="panel-body">
      <table class="table table-bordered table-dark">
          <thead>
            <tr>
              <th scope="col">#</th>
              <th scope="col">Name</th>
              <th scope="col">Status</th>
              <th scope="col">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php  foreach($item['children'] as $loc){ ?>
            <tr>
              <th scope="row">{{$loc['id']}}</th>
              <td>{{$loc['location_name']}}</td>
              <td><?php if($loc['status']==1){ ?>
                <span class='label label-success'><a href="updatestatus/{{$loc['id']}}/0" style="color: #FFF;">Active</a></span> <?php }else{?> 
                <span class='label label-danger'><a href="updatestatus/{{$loc['id']}}/1" style="color: #FFF;">In Active</a></span> <?php } ?>
              </td>
              <td>Edit | <a href="deletelocation/{{$loc['id']}}" style="color: #FF0000;">Delete</a></td>
            </tr>
            <?php } ?>
           
          </tbody>
        </table>


      </div>
      </div>
    </div>
    <?php $count++;} ?>
  </div> 
</div>
<div class="col-md-3">

  <h2>Add New Location</h2>
  <form action="{{route('addLocation')}}" method="post">
    @csrf
  <div class="form-group">
    <label for="email">Location City:</label>
    <select name="parent_id" class="form-control">
      <option>-Choose Location-</option>
      @foreach($location as $location)
      <option value="{{$location['id']}}">{{$location['location_name']}}</option>
      @endforeach
    </select>
  </div>
  <div class="form-group">
    <label for="pwd">Location Name:</label>
    <input type="text" class="form-control" name="location_name">
  </div>
  <button type="submit" class="btn btn-success">Submit</button>
</form>
</div>
</div>
</div>
    


    <div class="row mt-4">
    <h1>{{$locationArr[0]['location_name']}}</h1>
    <?php $locArr= array_chunk($locationArr[0]['children'],count($locationArr[0]['children'])/4); ?>
    <?php foreach($locArr as $item){ ?>
    <div class="col-12 col-md-3">
        <ul class="footer_links">
            <?php foreach($item as $loc){ ?>
            <li><a href="{{str_slug($loc['location_name'])}}">{{$loc['location_name']}}</a></li>
            <?php } ?>
        </ul>
    </div>
    <?php } ?>
    </div>
</body>
</html>
